<?php

// TODO: modernize session
session_start();

// TODO:
// 1. Show some apache & php server variables. Like: memory limit, <?php tag, etc.
// 2. d variable security: filter ../../ requests
// 3. Wrong folder error message

/* ???
  if($ajax_cat = $_GET['ajax_cat']) {
  echo "<h3>".$ajax_cat."</h3>";
  $d = dir("_old-projects_/".$ajax_cat);
  while (false !== ($file = $d->read())) {
  if ($file!="." && $file!="..")
  echo "<a href=\"?d=_old-projects_/".urlencode($ajax_cat)."/".$file."\">".$file."</a><br>";
  }
  exit;
  }
 */

// Icons images
if (filter_input(INPUT_GET, 'iof')) {
    $iof = array(
        'txt' => 'R0lGODlhDQAPAMQAAP7+/tXV1QcHB3x8fIKCgnR0dK6uru3t7bq6usHBwbi4uPDw8O7u7ufn576+vvr6+vv7+/j4+ISEhPn5+cjIyPT09ImJiZmZmfX19fPz8wMDA/b29vz8/AEBAQAAAP///yH5BAAAAAAALAAAAAANAA8AAAWG4KdlF0Z8w1eI39donwd4cXx4a8S137R9h4Tm9FlwIq1Hy9EhZD4CjafhERw+FI9FiYHwlBGEILURdDxSjxfhOQEgP17lk8B9OFUPWpDJZCUcABwTdz0tbBYLUFJnNBkBHRJAGB8AHxVxbAMMHxuKPJQUMAoBCKUGDgkJBjUeHWcaAnppHyEAOw==',
        'php' => 'R0lGODlhEAAQAPcAAI95D5WVlZF6D5N8D9e1FrqcE0E3B+7lufz8/HNkGiclHiYiFCMdBJCMdpB6DzcvBZ2EEDs5MikjBP3iYlFRUUhDLG9dC0dHR4eHh2RYIGZaIGxeHpGMdnhzXGpcH87Gn/bQHqWlpWJiYm1cCzEuJevGGaSkpDMrBbOdNcenFTQrBU9IKkpFLxgUAhsWA+7u7tW0Fq2SEvfRHnNzc3BwcPLnt/3YK/PpuT81BqGXZufSb+bbo//0w8eoFUxFJDQsBcLCwot1Du7diz4+Pf/vpYaGht26F7u7u3R0dLGWFYRxFktLSsa2a6+TEkdCLO/v77OmaGNdRk5IK5uDEP/tnJqCFbi4uPb29qWLE+neqUI6Eu7VXiwoF+XXkXhnGb6+vqGIE/Dioa+VGerblf/1xXVjDDoxBlZICKurq4dxDpZ/ELWYE0dGRCYiEGpZC0hDL+Tk5EZCLzowBnRlGSYjGffRIJ6enqCgoF1dXWlpaZJ7DyYhDufn509PT1ZICayREmtoW/zXJ9m3F21tbUxMTFJFC2dnZ05IMJV/HO3t7fr6+v7+/q2haf/lbiYkHczDmObCGLKysjQuE3dkDLiugr2fFPvxwnVlGX1uKXViDJZ+EIx3F+rFGHVlGlJSUkRCPZ6FEFtbW+XBGP7aMsWmFFFKKnp6eks/B8mrG0FAPaamptOyFsirJoN1N3RlH5WNbevitsWzYsWtO//xsqKIEaGhoc3Fn0RDQIODg5aWlvj4+E9DCJGRkWtaC4RvDWhoaD07MUpKSv/yt7e3t6aMEaKiooFuE29sXllLCY54D////wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAAAAAAALAAAAAAQABAAAAj/AJUJVMbHjqE+nvIUSzRwIIJcbBrYytIlVqtPIRoiQALoAKwas4RssYHqEK5FAgMcs/TB0aswXFzJgJHkjQllcG4dINNBwY4cbVhx6tFk05AnATjwuEEiChEpWuoQKEBsSilVIh4Jo0SHEZM9VUSRigFKzYYZwcZQgbLAh6RCaU4ZMDNpgJdQhHQ0moACERYCEnBkGpFMwCU8v2SNCgSihKAgLuTsKuMAgAZTdzBBMrIqRQEDDJD58QUgWYVIL1KJqbTmj54WD9xYKD3nwhVlaFiAoQWh1w8VJ84kMxbhyEBecZRoGiCgdLIEwGo1VDZsyQoPnRJkcEIByHSBuqwUCaExCMMXRQ0DAgA7',
        'js' => 'R0lGODlhDAAPAPcAAP/9//7//9PO1P//8///3wsLAff2/i0tAPv//4KDh0dLAfr3/igoIP/++xkcABEQAP/+/aWgpJ6cjdLN0/Py90VIA/Hv9CIkAJucjpCMjfHs8ufs5ra3l4aIc+Th7OXn5MfCyYeGcufk79bV48HAxZudePr85/v37Pj063V3bM7L1uXnuNfbqXd2cv//yPz73fP09nt+X8XEsPf1z46PbX1/af//9nl8Yefk9dDM2uXj19PSvpGTkNnU2nh7KsXFvf//1PL1pK+vjaGghOLhzG5zGfj8pfj11l9cV///5klIRuXlpYiJge7s14OCUv//s2ltI6alt9DQmoaFjbm6mBYWAAoIAP3/xMvPem5xPKilkrO0ey4vIfz8vP/++vn2///++OHm4FxbYMTCz3x6f+Lf6ICAdnp8iZudUmptPuvtxejmv///zy0uADEwEVJSUp2bhvPywpWUW56iTvf6q/z26rq6rtvb3Q0NAIyKj4eOh8O+xfL2u9vX5cXKxo2QdRUVAH6Cg7S6sPr8u/bz7NPQ4bOzl357jMbFw8rH0H6CNby5ypCRjM7RtsjGyVhUUQYIAMHBtdzgk1VRSPr3/4OCh1lYVubi+ZKPbOLe7DU4AN3cvggFAAgHAL/AxXZ4OdPX2Pn59/Tw5ZaVdmpnYKChX//+/wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAAAAAAALAAAAAAMAA8AAAjNAM8cmmKmw58aKXgkCKRnBABTAxrFuIGBUQAEYcYY8EKkhBoCJpi8CeBHhSkdVFhkSWOIi5JKARJB0LLiyiA+gEK0EGNqD5ghLqAocvDAChJTZXqIwtQlyBNJB14QsiBCwIAZpbAU8bHFjQ0AfSbUIbBkjhE6bEZN0pBJAIojctBoUuAkCak8lEA0gBPnU4U2a5o8IrPAQ4QTQi5IAbJJgiUKd3IAyLCDAx4aMhiYMvWhEI4vnDpVgVQglCkYG6JcWuToh51IiEh4AiUoIAA7',
        'img' => 'R0lGODlhEAAQAPcAAPb7//r9/1lic/7+/9/v//D4/yNv1WVufM7n/9ns/42Tn0Om98vO08zV/9Pp/7jb/6i/7/r6+q3W/8Db/7PX/MLh/+rs8FKr9rW5wq7W/9zu/6zV/vb29pacxsDEzHyVyL3N87rM89/m7AiU8tHb5oSj1tbM2ajP/3B5iF9neJzG9XWIu6qz9eLx/5eeyfb4+YOi1frNAYbG/oir3q7S/7PY/b3d/Zim06/D8N7v/+r1/wuL8b3e//Pz9LfJ8nmPwmZvfs3h/4Ccz9zJI8jk/+ns8LLY/+fGJ+7w8qfT/8DDzeTy/+Tp7t7h5rvZ/8Td/+nIKKvB8AKb+cPm/7LU/52t2ZWZxNvi6rrJ9Orv+EKW5ZnL/LbW/6nU/+7XY5OWwfkzBIfP+XyGlvDMKZek0m57rr/DgdbY3bnc/rHF8cng/7m7xLXI8jxsvWNzjO80Dq/V/dPH1F9DlHKCtaW97lfwT5mhzIrO/6mvubS4wOXUCJWdqX2Is9Xf6IuRtg6n4ouOuMZ6ekZGrJqo1ufGJvj4+Nbr/2v5Nd3jxrfZ/Hy78rO3v1hhc4ep3P8zAACZ/////wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAAAAAAALAAAAAAQABAAAAjoACE1GkhwRolGWSApXNhoIaQBAQA0UuQGT5MXChsqhAigwIIFWsQosZAxwsOIBXQ0aMCCz54zGQs5mhloSo4WGgxhQeEhI4eZYNo8enTHQRAQKTBk7DFTjoGhj9CoCcFoUUYkjt4IejpUxhMfVRXCKBLHxI4Rf4ZumcAmLCQhTJYQCFPnkJQLNZykcftBBIEEXmLoGaLiARccbn9cSYBoDKEjUMxkoBLF7Yo+DhDYSEQBzoYuNCC4nUMCAZEKPB4YkZDkBB0BeRSWqTLoBhk7LjpY+eIHEJA1ChkoOCCAkfHjAg4oYAApIAA7',
        'zip' => 'R0lGODlhDwAQAPcAAAAJAAAAFQADFUMAAEUAAK2qPwAIAAAAHwEAHP/9kgALABsFAAAQHQAPKv+5RgITAPf1ng8AAAATAAATIQATNf+0h7UrBhkAAAASHgANEf/jc6EtBgAJB5PJ7f/pxXvGpZAzAJYhEKIuH/PBYgAbHAAvAAAOAP74vv/NXWONjAMEAP/d0rLd/wASKVFPZ06HdP/i4RgJDhsADP/1mP/myfzIdBJFcBc4JT6Or//+qiBUagAIH//RS//NZwgLGgAKDL4pCP/ZoLMmAGkIACAcGX0/DkBNVfe/cgAYAJokCtu7TBEmK/zNiQ8AGBgKAJASHjZmevXLVfDSfAAJMP/ONHKxwKXV4QAXIJGkqv/esLLN2P/43AAKAJS/z7c8PgAKELI8IgALIBEAAAAHGf3RkAgAAAsEAAAQBBAOAAAPFcTX5X9QVpMzAP/d1QAiAC1RZ//vzYKu0f/jrv/1wMilJ//HcPLVYP/CUYZ7i/zFaP/WTy8AADAAAPHPUbOmI3coCvnSjZQsCZchAGNsi///vFNEe/jCOzeAUwceLEIAALEpKyIaJQAPLhQAALrA2vz0wwASMqnH0QAXACtRUlhKYwAKJ5YmEv/mz/e/aO3oZl9weP/JnpUtAKbJ3wEhOJamvUQAAAAJEuS8SgAAEjBZS//mr5o8ILzAyfvNbZw8DPDadP/agwARA6LJ5v/6v//Cl//Kad/HMQATFQAWHU+On////wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAAAAAAALAAAAAAPABAAAAjoAGsJHFiJwhVZYXYsGggmialaTlwM0oTlkyM8RGoJ2VTh1ZABiUAR2COjCaVTN4BYEMQJRCpLIgL9WVMrgBoOilbAcZVjxqo8UVQZqJWmy4QnMGicgJBAQ59YmUzUImFllJc2Wx4RkjIChR47ZmpliFQmxCUPcwDVgHXHkKhGtUJpibEhSykmdXo44EGFzoJaYzr5YCMnCJkjmFAp8VNARa0GrQQU4XNBTAQ0XCS5QTJrig0WiAYKBEDqxaEPVTrE8SR64AMFEkqw+rKkte1aZ1LcHojAyA9aLaAwmHS70IE3kHAw0oEhIAA7',
        'ie' => 'R0lGODlhEAAQAPcAAH+ux6HE5jKIzfX19crY/3SAsoK10EuItKnO/yaKwm7E62prmzOY7FJSg5jC1UOq/rvO48XFxb7K/ylFX63N/z3L/iif5khsib3K/23B5uz1/yac5UtsiLrK/3ez3yg9W4Sj1vP5/0KV5JS31oir3uvz+MbV+z2g2r3X5qa9z/3+/zGV7cTi/ypsrJTp/C+i62hql1Gm5X7Q+z94sWFjkLTS8DWR2dvt/0vB+XSUtOTy/4OmxH2BqzJdjzuY6p6vwff5/Hp+pzOc7LfM/z+67tPp/0uBrsbV/HGBsiZEZuz2/8nY/7DN/1tci6y2w8nX/p+02EBkibTM/8vq9ujw97/f/2vz/0mm5mNtnHWo3m9ynrPF7HGjyOHs9jBzsrbY6Tu57yp7xNvq+CJEaqvN/6jG46K33lWVwD+p9zKP2B9xnjOp3TO08CmW5VZWh1VWhsHQ9rrb+0mKxKW54X3O+6/V8COR0nR4o93d3Ud4pcjW/TGq8szl/x5gmoep3FmWvFhZiZWVlf///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAAAAAAALAAAAAAQABAAAAjlAAUJ9EOwIAkQBfAMEMjQjwqGDAtwibCwoUAqKAwY+IJEggiKDf+IFCmICJYFC3gEEujgjwsrFcCcEZShSwgJQVa2nLImQQILL4wIUqIBw51AJf4IAqAmgKAwD2zkEKOjg5ZASgGwsXMlRhoGD/JAuDEExkoAODb0Wcs2CoQiUmgESiFjT5s+Afjo1VuGD5MmgQ6cmCFkhZcAAhJfqMGCgpvAP5y08MEAjQIFE0bEqUKmQSA6OwTJ6TEmyQcOUOA8IYDAM5A6HrLomWNmyxETSwiwfrNSIKDfwIP/7i0wkPHjyI0HBAA7',
        'doc' => 'R0lGODlhEAAQAPcAAAVBm9zi9ENpuZOr5trm+zlZmJWkuXaR1Zm24FRspIml6QAxd+jw/Qc7iO/0/fz9/V+AzWaK1X+e5oOc2pOit9zo+4uk3Slht+Ls/Nbk+jlouOXt/Fd90bDA1qS73enw/Ojv/JWz4trn+5W04gcui3aKnKq5z0hzzP39/c7e+GZ9s9jl+tDg+a290p6433+n54+x4/L2/qi2zZamuzFatoCn56W0ylF2yl58w7fI9FR6zpyrwHuX2Nfl+rLC2Iyv5N3p+4qu5Ka2y/T4/czd+OHs/NPi+sfa+Mnb+dfl+9bc6ubu/OHq+7HB18rb+EFtxKW83Cc9bJGx4pmovoSr5uLs+9/q+9nm+6u60fDy+lx0q6260MnS4oWs5q6+1DdgrvP4/oit5fP3/unw/dbk+3ql6J243rrF1M7e+Xuk6dLh+q+83+Hr+9Li+qq+8e/1/T9ltO30/aG63eTt/PT3/tPi++nx/SBLmkxpp+fv/Hej6wQ1gghKsjZKZJCu1jVJY////wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAAAAAAALAAAAAAQABAAAAjyAAEJ9NGkg5cWWEzIEGIDhcABAxRI4BGBw40nF8TE2dHnAaABAkOG5EPHgYEzHRW4mQBBCYQACSzwGfKGQp+bEgJMwBFAQAA8Jfj48WMHioc+BwAdELDmjoUCXACA8fOhiJw/EQCpEKBlD40ogADE8LPEipk/OgAlKJBjz54tgBo48DMHCII/JwCR+JJlDxyBe4YyIRDijwaRIheAwFAhA4w/F/hIBkC5wZ4FQ5OoCfIn5BQGYxjk2cCmQg8jaLp0FjijCoahsP2kcFJjNSADBERcWUGmDgsiSI6ksU2hjQsEI6T8CEPlRRk9tv9In059ekAAOw==',
        'levelup' => 'R0lGODlhEAAQAPcAAP//6g8AAP//tf/8/f/+////qv/++xgMAAQDAAYBAP//lQYCAAgAAAYDABIFAPn6nBIEABYMAAcGABMOAMrAj4WHSB8LAN/Na/35jv//if/9/wgEAP/8/5+bUf//8fv/sJWSXf/99P//jv//o///jP7/egQAAMPIavP1kMXBhMHGavz/e8bBgf//1trdhP/9xv//xf/08t3Rp/b8ov3/mv//vP/+7/z67v//r5aWVvn62//698e9pPz/lvf7ppaYWZWaV/DglfDhkPXx6P/6g4iLMMvFi+buh8zNff//5NzSe/z/iPHw69jXev//6P/9+v/6/Q4GAP/7+fX199fMjvb189bJg/f/i/v71+DKmNHXf7+4XvbnwJaaQ5WXRO/di////eTUhvL3kPjvrpycXNPRh/Py+P//zOLPp//1lv//sxEEAA4AAPr/mw4CAOLdmw8MBQ4DAPj2o///nAQGAMS/h9vRlv//rv/56f/89f7968/Cn///xu/ehPr+se7zl/Lx1f//7P3xn/Ls+P//uPvxnIiLRv//+IuLV87Anf//pdDYaP/95P/33s7JePLx9ggDAPbnjPbug///kf//0f/uvvXwrhAAAJqWTe3jnu7eqv/5whEAAJaWcr/DYv/0zP//wxQAAN3dg/3/mf/+8e3cgvjm0JSYXf//mODSiZWUW+vllxQQAP//qMW2c//+24mGOQ0FAOjbafn45pWQTBIGAAsAANrgdP//+v//q///of738f358I+PUaKhcQQEAIOFU5SOUPz0q///5//yf///iP7mzMLGesfKkQcCANHTgLi/TZGTUv///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAAAAAAALAAAAAAQABAAAAj/AJsJHEiwYDNeNgAASqJj2KxDYB4RMENgSrMnjHxVMATkFB0sAIZoGMSBQDMAiD7guOOjRi8JLV45IbWrSrMcJxYt84QkRZ1gtDrAwgRCT7MuV0hMKkDoDKUxAhTNweAIQbMiS0oUawIKAhc+hSSJILKFVTMvClZkEOAmgIVKQXTJIqZkQTMybRSMiHUpAKdQL76kuZDKrqo/ox5sahQghrEsavoUcJWgGbNcPVpZ+sTGFBoYQiKVsjKh2Y8ZNMQIeBOh1oE1DgSFsVMZmJ8jqG6hECVnlbBMVDTt2dCsE7JjWlS4UFaGhREKMhLxMNEs0C8EDRokg8QgShwHtg4wFoDT7EYeXAYM7BgwAMoAKSHweGDSLCAAOw==',
        'folder' => 'R0lGODlhDwANAKIGAP/AgICAAMDAQP///wAAAP//gP///wAAACH5BAEAAAYALAAAAAAPAA0AAAM7aBbMpBCGQUsJLy7Bu2jNQo1kJRCTpa4FcKZsK79DbAEtzeJ8oe8yF6p2C7aEk54SgPQ4PQ+CdEqlJgAAOw==',
        'view' => 'R0lGODlhEAAQAKIDAAAAAISEhMbGxhgVAP///wAAAAAAAAAAACH5BAEAAAMALAAAAAAQABAAAANFCLo7/gOQSYmCTtYJBMDaxkDhxpGm1TwlFwhC8GViMMUyXXUE/M6tDizm0VEALwEhkIuYkD6kcbeQfVqiCGO7zcww4EcCADs=',
        'print_16' => 'R0lGODlhEAAQANUxACIiIv///5GRkVRUVDs+QAseQ5eXl9DQ0KHK6ezs7NLR0vf393uJlN/f32pqajpXkJHB5N/e39LS0n+Zy9Hb7bnE2KDL6Z+fn5mt1qDK6XePv8nU6cfGx4aYvqO11zxYkWmDs5rC46vM6cfFxqHK6sbFxuzs7ff4+KHL6ff3+HijzMbGxpHB5cbGx5nC43mizarL6f///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAADEALAAAAAAQABAAAAaRwJgwJigai8MkMcBsCpTCgWBxWqQWC8EAKk2YEuCEliuIRBroxlgplSjeCslauMFIW6NVicPRYjYxFBMMAASGh4YADBMUHoQAkJGSih4aME2YmSIaIC4IFggZCCifoiQPISAdLxAsBa+wrxAQKh0VHw8ABru8uwAPHxUxDhcAB8fIxwAXDkMOAJlNAM1Jk5NDQQA7',
        'save_16' => 'R0lGODlhEAAQANUAAP///y4uL1FSU725vzk6O6ohLOV/h+vs7jM0Nby9vtXW3PHx9LCytpmamufo7zAxMhgYGYiJiZQdJtV8g++Oifr4++fo7i0uL8ULDpGSlMEKDfO6w2JgZf/+9fjY2/JyeP/46issLfzf3P/86Xt5fTIzNMwfFv/y2/fr9SkqKujo7vrO0yYnKP3i7O+Dgo6OkPa2uNEaHf///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAADIALAAAAAAQABAAAAZ3wEZAUigaj5KAMDIxOJ/QSSQQEAwA2Kx2IKBatWAA13sNZ8fVMqATRn/NWLfa7CYN7vg8p1sl+P+AfnwCfhkLh4gvISWDfg4QECoODhYODxeNBAoICAqenggPmQycDKamoV0NCAQBB6+wr6GtMkIyCbi5uCksSkEAOw==',
        'db' => 'R0lGODlhEAAQAOYAAGFhYZ6enlNTU2xsbOTk5MPDw3Z2dltbW9jY2M/Pz+7u7vv7+/f398vLy7m5uaKiopGRkbCwsGpqan9/f8XFxa6urubm5nBwcNHR0aampjY2NoeHh3R0dDo6OqSkpHx8fM3NzUtLS/n5+cHBwdzc3ImJiVFRUU1NTY2NjaCgoGhoaLu7u0JCQrS0tJWVlTQ0NOrq6m5ubo+Pj7Kysr+/v+Dg4FdXV9bW1iMjI/X19f39/aqqqpeXl2RkZC4uLkdHR11dXVlZWTg4OJOTk09PT3h4eHJycoODg/Hx8ezs7B0dHXp6ej4+PtTU1FVVVV9fX97e3snJyQAAAOLi4qysrIGBgaioqGZmZv///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAAAAAAALAAAAAAQABAAAAfGgFiCWDQBGQUFVgEUg4MVEzIrJDBTUR4fSxGDLUmNjTlQNSWCMSkino0EQYJAPhIZCQSoVAKCIDhPEA4NDKkAGIIUPCEdJj0fQxAGJxolO4JVBSQRyioHNlcoDQoAggcaRyMECqgztVhWSlcuDiC9gyJGVoIIAz8mFxtWDRgOGwIDEgjqoODGgwkDADgJQURCBR1CBAEQEAABkneNCgzwxsLAgxFNFnjy0A3LggtSmBR5IBALggAvOIhsZMEKhytXDFix0CgQADs=',
    );
    header("Content-type: image/png");
    header("Cache-control: public");
    header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + (60 * 60 * 24))); // 1 hour
    header("Last-Modified: " . date("r", filemtime(__FILE__)));
    echo base64_decode($iof[filter_input(INPUT_GET, 'iof')]);
    exit;
}

//if (!eregi("\.\./$", $_GET['d']) && !eregi("\.\.$", $_GET['d']) && !eregi("^[a-z]:", $_GET['d']))
//	chdir(urldecode($_GET['d']));

$dir = $_POST['d'] ?? ($_GET['d'] ?? null);

// Define current path.
if ($dir && is_dir(HOME_PATH . '/' . $dir)) {
    define('CURRENT_PATH', rtrim(HOME_PATH . '/' . $dir, '/'));
} else {
    define('CURRENT_PATH', HOME_PATH);
}

// Define relative path.
if (CURRENT_PATH === HOME_PATH) {
    define('RELATIVE_PATH', null);
} else {
    define('RELATIVE_PATH', $dir);
}

// Change directory.
chdir(CURRENT_PATH);

// ???
$password = "";
//echo("Time: ".time()."<br>");
// ???
//if ( $_POST['pass'] == $password ) {
//    $_SESSION['sess'] = 1;
//}

// Get IP subnet.
$a = explode(".", $_SERVER['REMOTE_ADDR']);
$user_ip = (int)($a[0] . $a[1] . $a[2]);

// TODO: Add ban by IP

// Get list of files and folders in root directory
$ds = $fs = array();
$d = dir(CURRENT_PATH);
while (false !== ($entry = $d->read())) {
    if ($entry !== '.' && $entry !== '..') {
        // Add file to files array.
        if (is_file($entry)) {
            $fs[$entry] = array(
                'relative_path' => trim(RELATIVE_PATH . '/' . $entry, '/'),
                'full_path' => CURRENT_PATH . '/' . $entry,
                'name' => $entry,
            );
        }
        // Add folder to folders array.
        if (is_dir($entry) && $entry !== '_old-projects_') {
            $ds[$entry] = array(
                'relative_path' => trim(RELATIVE_PATH . '/' . $entry, '/'),
                'full_path' => CURRENT_PATH . '/' . $entry,
                'name' => $entry,
            );
        }
    }
}
$d->close();

// Sort files and folders in ASC order
asort($fs);
asort($ds);

// Set options.
$view_mode = $_POST['m'] ?? ($_GET['m'] ?? 'simple');
// Full access mode.
$fa = $_POST['fa'] ?? ($_GET['fa'] ?? null);
if ($fa === 'on') {
    $fa_mode = true;
} else {
    $fa_mode = false;
}
// ??? Link type.
if ($fa_mode) {
    $link_mode = false;
} else {
    $link_mode = true;
}

// If page opened on local server NOT from internet
if ($user_ip === 1921681 || $user_ip === 12700 || $_SESSION['sess'] === 1) {
    /* ???
    if ($d = $_GET['z']) {
        chdir(".");
        $data = date("Ymd_His");
        $path = $d;
        if (eregi("\/", $d))
            $d = eregi_replace(".*[\/]([a-zA-Z0-9_-]+)$", "\\1", $d);
        $fname = "d:/fafi/tmp_" . $d . ".zip";
        $f_list = array();

        if ($handle = opendir($root_dir . $path . "/")) {
            while (false !== ($file = readdir($handle))) {
                if ($file == "passwords.php") {
                    ## Clean passwords.php for zip file
                    copy($root_dir . $path . "/passwords.php", $root_dir . $path . "/tmp_passwords.php");
                    $filename = $root_dir . $path . "/passwords.php";
                    $contents = join(file($filename));
                    $contents = eregi_replace("\'[^;]+\'", "''", $contents);
                    $f = fopen($filename, 'w+');
                    fwrite($f, $contents);
                    fclose($f);
                    ## Creates dump
                    require($root_dir . $path . "/tmp_passwords.php");
                    if ($database_host && $database_user) {
                        mysql_connect($database_host, $database_user, $database_pass);
                        mysql_select_db($database_name);
                    }
                    $r = mysql_query("SHOW TABLES");
                    while ($rr = mysql_fetch_row($r)) {
                        $sql_dump .= sqldumptable($rr[0], ($rr[0] == 'options') ? 1 : 0);
                    }
                    ## Clean dump data from table `options`
                    //$sql_dump = eregi_replace("(INSERT INTO `options` VALUES\('[0-9]+',')[^;]*(',')[^;@]*('\);)","\\1\\2\\3",$sql_dump);
                    $f = fopen($root_dir . $path . "/db_dump.sql", 'w+');
                    fwrite($f, $sql_dump);
                    fclose($f);

                    //$f_list[] = $root_dir.$path."/db_dump.sql";
                    $f_list[] = "db_dump.sql";
                }

                if ($file !== "." && $file !== ".." && $file != "docs" && $file != $d . ".zip" && $file != "tmp_passwords.php" && $file != "db_dump.sql")
                    $f_list[] = $file; //$f_list[] = $root_dir.$path."/".$file;
            }
            closedir($handle);
        }

        $fname = "d:/fafi/tmp_" . $d . ".zip";
        $archive = new PclZip($fname);
        chdir($root_dir . $path . "/");
        $archive->create($f_list);
        chdir(".");
        copy($fname, $root_dir . $path . "/" . $d . ".zip");
        unlink($fname);
        copy($root_dir . $path . "/tmp_passwords.php", $root_dir . $path . "/passwords.php");
        unlink($root_dir . $path . "/tmp_passwords.php");
        unlink($root_dir . $path . "/db_dump.sql");
        ?>
        <form name="formtocopy" action="">
            <textarea name="texttocopy"><?= "http://83.218.201.35/" . $path . "/" . $d . ".zip"; ?></textarea>
            <br>
        </form>
        <script language="JavaScript">

            function copy(inElement) {
                if (inElement.createTextRange) {
                    var range = inElement.createTextRange();
                    if (range)
                        range.execCommand('Copy');
                } else {
                    var flashcopier = 'flashcopier';
                    if (!document.getElementById(flashcopier)) {
                        var divholder = document.createElement('div');
                        divholder.id = flashcopier;
                        document.body.appendChild(divholder);
                    }
                    document.getElementById(flashcopier).innerHTML = '';
                    var divinfo = '<embed src="__stuff/_clipboard.swf" FlashVars="clipboard=' + escape(inElement.value) + '" width="0" height="0" type="application/x-shockwave-flash"></embed>';
                    document.getElementById(flashcopier).innerHTML = divinfo;
                }
            }
            copy(document.formtocopy.texttocopy);
            document.formtocopy.texttocopy.style.display = "none";
            alert("There was created zip file and file URL was copied to clipboard.");
            location.href =<?php echo"'";
        echo$_SERVER['HTTP_REFERER'];
        echo"'"; ?>;
        </script>
        <?php
    //                chdir(".");
    //                $data = date("Ymd_His");
    //                $fname = "d:/fafi/__common/".$d.".zip";
    //                $archive = new PclZip($fname);
    //                $archive->create(array($d), '', '');
    //                $fp = tmpfile();
    //                fclose($fp);
    //                header("Content-type: application/x-gzip");
    //                header("Content-Disposition: filename=".$d.".zip");
    //                header("Content-Transfer-Encoding: binary");
    //                header("Content-Length: ".filesize($fname));
    //                readfile($fname);
    //                unlink($fname);
        die();
    }*/
    /* ???
    if ($v = $_GET['v']) {
        //echo $v; die;
        $fp = fopen($v, 'r');
        $a = fread($fp, filesize($v));
        fclose($fp);
        $pi = pathinfo($v);
        $ext = strtolower($pi['extension']);
        if (strtolower($ext) == 'php') {
            echo "<nobr>";
            highlight_string($a);
            die;
        }
        echo htmlentities($a);
        die();
    }
    */
    require ABSPATH . '/template/index.php';
}

function sqldumptable($table, $sql_d)
{
    $tabledump = "DROP TABLE IF EXISTS `$table`;\n";
    $tabledump .= "CREATE TABLE `$table` (\n";
    $firstfield = 1;
    $champs = mysql_query("SHOW FIELDS FROM `$table`");
    while ($champ = mysql_fetch_array($champs)) {
        if (!$firstfield) {
            $tabledump .= ",\n";
        } else {
            $firstfield = 0;
        }
        $tabledump .= "   `$champ[Field]` $champ[Type]";
        if ($champ['Null'] != "YES") {
            $tabledump .= " NOT NULL";
        }
        if (!empty($champ['Default'])) {
            $tabledump .= " default '$champ[Default]'";
        }
        if ($champ['Extra'] != "") {
            $tabledump .= " $champ[Extra]";
        }
    }
    @mysql_free_result($champs);
    $keys = mysql_query("SHOW KEYS FROM `$table`");
    while ($key = mysql_fetch_array($keys)) {
        $kname = $key['Key_name'];
        if ($kname != "PRIMARY" and $key['Non_unique'] == 0) {
            $kname = "UNIQUE|`$kname`";
        }
        if (!is_array($index[$kname])) {
            $index[$kname] = array();
        }
        $index[$kname][] = $key['Column_name'];
    }
    @mysql_free_result($keys);
    while (list($kname, $columns) = @each($index)) {
        $tabledump .= ",\n";
        $colnames = implode($columns, ",");
        if ($kname == "PRIMARY") {
            $tabledump .= "   PRIMARY KEY (`" . eregi_replace(',', '`,`', $colnames) . "`)";
        } else {
            if (substr($kname, 0, 6) == "UNIQUE") {
                $kname = substr($kname, 7);
            }
            $tabledump .= "   KEY $kname (`" . eregi_replace(',', '`,`', $colnames) . "`)";
        }
    }
    $tabledump .= "\n);\n\n";

    if ($sql_d == 1) {
        $rows = mysql_query("SELECT * FROM `$table`");
        $numfields = mysql_num_fields($rows);
        while ($row = mysql_fetch_array($rows)) {
            $tabledump .= "INSERT INTO `$table` VALUES(";
            $cptchamp = -1;
            $firstfield = 1;
            while (++$cptchamp < $numfields) {
                if (!$firstfield) {
                    $tabledump .= ",";
                } else {
                    $firstfield = 0;
                }
                if (!isset($row[$cptchamp])) {
                    $tabledump .= "NULL";
                } else {
                    $tabledump .= "'" . mysql_escape_string($row[$cptchamp]) . "'";
                }
            }
            $tabledump .= ");\n";
        }
        @mysql_free_result($rows);
    }

    //$fff = fopen("dump.sql","w+"); fwrite($fff,$tabledump);

    return $tabledump;
}

// Returns folder/file size
function get_size($name)
{
    // If it's a file then return it's size
    if (is_file($name)) {
        return filesize($name);
    } // If it's a folder then we should calculate files & folders in it
    elseif (is_dir($name)) {
        return 0;
        $handle = opendir($name);
        while (false !== ($file = readdir($handle))) {
//			if( is_dir($name . '/' . $file) && $file != '..' && $file != '.' ) $size_sum += get_size($name . '/' . $file);
//			else
            if (is_file($name . '/' . $file)) {
                $size_sum += filesize($name . '/' . $file);
            }
        }
        closedir($handle);

        return $size_sum;
    }

    return 0;
}

// Get folder/file size and returns formated string
function format_size($name)
{
    $size = get_size($name);
    if ($size) {
        if ($size < 1024) {
            return $size .= " b";
        } elseif ($size >= 1024 && $size < 1024 * 1024) {
            return $size = number_format($size / 1024, 2) . " Kb";
        } elseif ($size >= 1024 * 1024 && $size < 1024 * 1024 * 1024) {
            return $size = number_format($size / (1024 * 1024), 2) . " Mb";
        } else {
            return $size = number_format($size / (1024 * 1024 * 1024), 2) . " Gb";
        }
    } else {
        return "";
    }
}
